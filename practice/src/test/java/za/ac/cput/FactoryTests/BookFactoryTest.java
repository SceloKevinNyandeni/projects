package za.ac.cput.FactoryTests;

import org.junit.Test;
import za.ac.cput.Domain.Author;
import za.ac.cput.Domain.Book;
import za.ac.cput.Domain.Publisher;
import za.ac.cput.Factory.BookFactory;

import java.util.Collections;

import static org.junit.Assert.*;

public class BookFactoryTest {

    @Test
    public void testCreateBook() {
        // Dummy Author and Publisher
        Author author = new Author.AuthorBuilder()
                .setAuthorName("R. Burger")
                .build();

        Publisher publisher = new Publisher.PublisherBuilder()
                .setPublisherName("Naidoo and co publishers")
                .build();

        // Use the factory to create a book
        Book book = BookFactory.createBook(
                12L,
                "Java Introduction",
                Collections.singletonList(author),
                Collections.singletonList(publisher),
                2021,
                120.00
        );

        assertNotNull(book);
        assertEquals(Long.valueOf(12), book.getBookID());
        assertEquals("Java Introduction", book.getTitle());
        assertEquals(1, book.getAuthors().size());
        assertEquals("R. Burger", book.getAuthors().get(0).getAuthorName());
        assertEquals(1, book.getPublishers().size());
        assertEquals("Naidoo and co publishers", book.getPublishers().get(0).getPublisherName());
        assertEquals(2021, book.getYear());
        assertEquals(120.00, book.getPrice(), 0.01);
    }

    //test for null or empty title
    @Test
    public void testCreateBookWithNullTitle() {

        // Dummy Author and Publisher
        Author author = new Author.AuthorBuilder()
                .setAuthorName("R. Burger")
                .build();

        Publisher publisher = new Publisher.PublisherBuilder()
                .setPublisherName("Naidoo and co publishers")
                .build();

        Book book = BookFactory.createBook(
                13L,
                null,
                Collections.emptyList(),
                Collections.emptyList(),
                2021,
                120.00
        );
        assertNull(book);
    }

    // Test for invalid year
    @Test
    public void testCreateBookWithInvalidYear() {

        // Dummy Author and Publisher
        Author author = new Author.AuthorBuilder()
                .setAuthorName("R. Burger")
                .build();

        Publisher publisher = new Publisher.PublisherBuilder()
                .setPublisherName("Naidoo and co publishers")
                .build();

        Book book = BookFactory.createBook(
                14L,
                "Java Introduction",
                Collections.singletonList(author),
                Collections.singletonList(publisher),
                1800, // Invalid year
                120.00
        );
        assertNotNull(book);
    }
}

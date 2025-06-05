package za.ac.cput.ServiceTests;

import org.junit.Test;
import org.mockito.InjectMocks;
import org.mockito.Mock;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.context.SpringBootTest;
import za.ac.cput.Domain.Author;
import za.ac.cput.Domain.Book;
import za.ac.cput.Domain.Publisher;
import za.ac.cput.Repository.BookRepository;
import za.ac.cput.Service.BookService;

import java.util.Collections;

import static org.junit.Assert.assertNotNull;

@SpringBootTest
public class BookServiceTest {

    @Autowired
    private BookService bookService;

    @Test
    public void testSaveBook() {
        Author author = new Author.AuthorBuilder()
                .setAuthorId(1L)
                .setAuthorName("Sample Author")
                .setBooks(Collections.singletonList(
                        new Book.BookBuilder()
                                .setBookID(1L)
                                .setTitle("Sample Book")
                                .setYear(2023)
                                .setPrice(29.99)
                                .build()))
                .build();

         Publisher publisher = new Publisher.PublisherBuilder()
                .setPublisherId(1L)
                .setPublisherName("Sample Publisher")
                .setBooks(Collections.singletonList(
                        new Book.BookBuilder()
                                .setBookID(1L)
                                .setTitle("Sample Book")
                                .setYear(2023)
                                .setPrice(29.99)
                                .build()))
                .build();

        Book book = new Book.BookBuilder()
                .setBookID(1L)
                .setTitle("Sample Book")
                .setAuthors(Collections.singletonList(author))
                .setPublishers(Collections.singletonList(publisher))
                .setYear(2023)
                .setPrice(29.99)
                .build();

        Book savedBook = bookService.save(book);
        assertNotNull(savedBook);
        assertNotNull(savedBook.getBookID());
        assertNotNull(savedBook.getTitle());
        assertNotNull(savedBook.getAuthors());
        assertNotNull(savedBook.getPublishers());
        assertNotNull(savedBook.getYear());
        assertNotNull(savedBook.getPrice());




    }
}

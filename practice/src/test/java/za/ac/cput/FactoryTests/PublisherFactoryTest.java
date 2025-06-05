package za.ac.cput.FactoryTests;

import org.junit.Test;
import za.ac.cput.Domain.Book;
import za.ac.cput.Domain.Publisher;

import java.util.Collections;

import static org.junit.Assert.*;


public class PublisherFactoryTest {

    @Test
    public void createPublisher() {

        Publisher publisher = new Publisher.PublisherBuilder()
                .setPublisherId(1L)
                .setPublisherName("Sample Publisher")
                .setBooks(Collections.emptyList()) // Assuming books can be null for this test
                .build();

         assertNotNull(publisher);
         assertEquals(Long.valueOf(1), publisher.getPublisherId());
         assertEquals("Sample Publisher", publisher.getPublisherName());
            assertEquals(0, publisher.getBooks().size());
    }

    @Test
    public void createPublisherWithBooks(){
        Publisher publisher = new Publisher.PublisherBuilder()
                .setPublisherId(2L)
                .setPublisherName("Another Publisher")
                .setBooks(Collections.singletonList(new Book.BookBuilder()
                        .setBookID(1L)
                        .setTitle("Sample Book")
                        .setYear(2023)
                        .setPrice(29.99)
                        .build())) // Assuming books can be null for this test
                .build();

        assertNotNull(publisher);
        assertEquals(Long.valueOf(2), publisher.getPublisherId());
        assertEquals("Another Publisher", publisher.getPublisherName());
        assertEquals(1, publisher.getBooks().size());
        assertEquals("Sample Book", publisher.getBooks().get(0).getTitle());
        assertEquals(2023, publisher.getBooks().get(0).getYear());
        assertEquals(29.99, publisher.getBooks().get(0).getPrice(), 0.01);
    }

    @Test
    public void createPublisherNullName(){
        Publisher publisher = new Publisher.PublisherBuilder()
                .setPublisherId(2L)
                .setPublisherName(null)
                .setBooks(Collections.singletonList(new Book.BookBuilder()
                        .setBookID(1L)
                        .setTitle("Sample Book")
                        .setYear(2023)
                        .setPrice(29.99)
                        .build())) // Assuming books can be null for this test
                .build();

        assertNotNull(publisher);
        assertEquals(Long.valueOf(2), publisher.getPublisherId());
        assertNull(publisher.getPublisherName());
        assertEquals(1, publisher.getBooks().size());
        assertEquals("Sample Book", publisher.getBooks().get(0).getTitle());
        assertEquals(2023, publisher.getBooks().get(0).getYear());
        assertEquals(29.99, publisher.getBooks().get(0).getPrice(), 0.01);
    }

    }

